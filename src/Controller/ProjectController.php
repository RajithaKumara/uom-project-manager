<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

/**
 * @Route("/project")
 */
class ProjectController extends AbstractController
{
    /**
     * @Route("/", name="project_list")
     */
    public function index(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findAll();
        return $this->render(
            'project/index.html.twig',
            [
                'projects' => $projects
            ]
        );
    }

    /**
     * @Route("/show/{id}", name="project_show")
     * @param $id
     * @return Response
     */
    public function showAction($id, ProjectRepository $projectRepository)
    {
        $project = $projectRepository->find($id);
        $projectDeleteForm = $this->_getProjectDeleteForm($project);
        return $this->render(
            'project/show.html.twig',
            [
                'project' => $project,
                'delete_form' => $projectDeleteForm->createView()
            ]
        );
    }

    /**
     * Creates a new Project entity.
     *
     * @Route("/create", name="project_create",methods={"GET", "POST"})
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        $project = new Project();
        $form = $this->_getProjectCreateForm($project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();

            return $this->redirectToRoute('project_show', ['id' => $project->getId()]);
        }

        return $this->render(
            'project/create.html.twig',
            [
                'project' => $project,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Deletes a Project entity.
     *
     * @Route("/delete/{id}", name="project_delete", methods={"DELETE"})
     * @param Request $request
     * @param Project $project
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Project $project)
    {
        $form = $this->_getProjectDeleteForm($project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($project);
            $em->flush();
        }

        return $this->redirectToRoute('project_list');
    }

    /**
     * Displays a form to edit an existing Project entity.
     *
     * @Route("/edit/{id}", name="project_edit", methods={"GET", "POST"})
     * @param Request $request
     * @param Project $project
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, Project $project)
    {
        $editForm = $this->_getProjectCreateForm($project);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('project_edit', ['id' => $project->getId()]);
        }

        return $this->render(
            'project/edit.html.twig',
            [
                'project' => $project,
                'edit_form' => $editForm->createView(),
            ]
        );
    }

    private function _getProjectDeleteForm(Project $project)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('project_delete', ['id' => $project->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }

    private function _getProjectCreateForm(Project $project)
    {
        return $this->createFormBuilder($project)
            ->add('name', TextType::class)
            ->add('description', TextType::class)
            ->add('startDate', DateType::class)
            ->add('cost', NumberType::class)
            ->getForm();
    }
}
